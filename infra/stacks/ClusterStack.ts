import { Construct } from "constructs";
import { TerraformStack } from "cdktf";
import { Subnet } from "@cdktf/provider-aws/lib/subnet";
import { EcrRepository } from "@cdktf/provider-aws/lib/ecr-repository";
import { SecurityGroup } from "@cdktf/provider-aws/lib/security-group";
import { EcsCluster } from "@cdktf/provider-aws/lib/ecs-cluster";
import { EcsTaskDefinition } from "@cdktf/provider-aws/lib/ecs-task-definition";
import { EcsService } from "@cdktf/provider-aws/lib/ecs-service";
import { AwsProvider } from "@cdktf/provider-aws/lib/provider";

interface ClusterStackProps {
    region: string;
    publicSubnet: Subnet;
    privateSubnet: Subnet;
    apiRepository: EcrRepository;
    apiSecurityGroup: SecurityGroup;
    databaseSecurityGroup: SecurityGroup;
    version: string;
}

export class ClusterStack extends TerraformStack {
    constructor(
        scope: Construct,
        id: string,
        {
            region,
            publicSubnet,
            privateSubnet,
            apiRepository,
            apiSecurityGroup,
            databaseSecurityGroup,
            version,
        }: ClusterStackProps
    ) {
        super(scope, id);

        new AwsProvider(this, "AWS", {
            region,
        });

        const cluster = new EcsCluster(this, "ecs-cluster", {
            name: "my-cluster",
        });

        const taskDefinition = new EcsTaskDefinition(this, "ecs-api-task-definition", {
            family: "webservers",
            containerDefinitions: JSON.stringify([
                {
                    name: "moooood-api-container",
                    image: `${apiRepository.repositoryUrl}:${version}`,
                    memory: 512,
                    cpu: 256,
                    essential: true,
                    portMappings: [
                        {
                            containerPort: 80,
                            hostPort: 80,
                        },
                    ],
                },
            ]),
            tags: { Name: "moooood-api-task-definition" },
        });

        new EcsService(this, "ecs-api-service", {
            name: "moooood-api-service",
            cluster: cluster.id,
            desiredCount: 1,
            launchType: "FARGATE",
            taskDefinition: taskDefinition.arn,
            networkConfiguration: {
                subnets: [publicSubnet.id, privateSubnet.id],
                assignPublicIp: true,
                securityGroups: [apiSecurityGroup.id, databaseSecurityGroup.id],
            },
            tags: { Name: "moooood-api-service" },
        });
    }
}

export default ClusterStack;
