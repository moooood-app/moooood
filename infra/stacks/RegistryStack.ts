import { Construct } from "constructs";
import { TerraformStack } from "cdktf";
import { EcrRepository } from "@cdktf/provider-aws/lib/ecr-repository";
import { AwsProvider } from "@cdktf/provider-aws/lib/provider";

interface RegistryStackProps {
  region: string;
}

export class RegistryStack extends TerraformStack {
  public readonly apiRepository: EcrRepository;

  constructor(scope: Construct, id: string, { region }: RegistryStackProps) {
    super(scope, id);

    new AwsProvider(this, "AWS", {
      region,
    });

    this.apiRepository = new EcrRepository(this, "moooood-api-repository", {
      name: "moooood-repo",
    });
  }
}

export default RegistryStack;