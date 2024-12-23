import { Construct } from "constructs";
import { TerraformStack } from "cdktf";
import { KmsKey } from "@cdktf/provider-aws/lib/kms-key";
import { SecretsmanagerSecret } from "@cdktf/provider-aws/lib/secretsmanager-secret";
import { AwsProvider } from "@cdktf/provider-aws/lib/provider";

interface SecretsStackProps {
    region: string;
    kmsKey: KmsKey;
}

export class SecretsStack extends TerraformStack {
    public readonly rdsMasterCredentials: SecretsmanagerSecret;

    constructor(scope: Construct, id: string, { region, kmsKey }: SecretsStackProps) {
        super(scope, id);

        new AwsProvider(this, "AWS", {
            region,
        });

        this.rdsMasterCredentials = new SecretsmanagerSecret(this, "rds-master-credentials", {
            name: "rds-master-credentials",
            kmsKeyId: kmsKey.id,
            description: "RDS master credentials",
        });

        this.rdsMasterCredentials.addOverride("generate_secret_string", {
            password_length: 16,
            exclude_characters: "/@\"\\",
            include_space: false,
            require_each_included_type: true,
        });
    }
}

export default SecretsStack;